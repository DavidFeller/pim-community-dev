import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, wait, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../../test-utils';
import {ScopeList} from '@src/connect/components/ScopeList';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

jest.mock('@src/connect/components/ScopeList', () => ({
    ...jest.requireActual('@src/connect/components/ScopeList'),
    ScopeList: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected settings renders with scopes', async () => {
    const scopes = [
        {
            icon: 'catalog_structure',
            type: 'view',
            entities: 'catalog_structure',
        },
    ];

    const fetchConnectedAppScopeMessagesResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_all_connected_app_scope_messages?connectedAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: scopes,
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppScopeMessagesResponses,
    });

    const connectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope 1'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    renderWithProviders(<ConnectedAppSettings connectedApp={connectedApp} />);
    await wait(() => expect(ScopeList).toHaveBeenCalledTimes(1));

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
            {exact: false}
        )
    ).toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: scopes,
            itemFontSize: 'default',
        },
        {}
    );
});

test('The connected app settings renders without scopes', async () => {
    const fetchConnectedAppScopeMessagesResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_all_connected_app_scope_messages?connectedAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: [],
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppScopeMessagesResponses,
    });

    const connectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: [],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    renderWithProviders(<ConnectedAppSettings connectedApp={connectedApp} />);
    await waitForElement(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')
    );

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
            {exact: false}
        )
    ).toBeInTheDocument();
    expect(ScopeList).not.toHaveBeenCalled();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope'
        )
    ).toBeInTheDocument();
});
