import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, wait, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../test-utils';
import {ConnectedAppPage} from '@src/connect/pages/ConnectedAppPage';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn().mockReturnValue({isEnabled: () => true}),
}));

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useParams: jest.fn().mockReturnValue({connectedAppId: '0dfce574-2238-4b13-b8cc-8d257ce7645b'}),
}));

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppContainer', () => ({
    ConnectedAppContainer: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app page renders with a connected app', async () => {
    const connectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: [
            {
                icon: 'catalog_structure',
                type: 'view',
                entities: 'catalog_structure',
            },
        ],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectedAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: connectedApp,
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await wait(() => expect(ConnectedAppContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
});

test('The connected app page renders with internal api errors', async () => {
    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectedAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            reject: true,
            json: {},
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await waitForElement(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    );

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});
