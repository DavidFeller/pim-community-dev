import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchConnectedApp} from '@src/connect/hooks/use-fetch-connected-app';

test('it fetches the connected app', async () => {
    const expectedConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
    };

    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectedAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: expectedConnectedApp,
        },
    });
    const {result} = renderHook(() => useFetchConnectedApp('0dfce574-2238-4b13-b8cc-8d257ce7645b'));
    const connectedApp = await result.current();

    expect(connectedApp).toStrictEqual(expectedConnectedApp);
});
