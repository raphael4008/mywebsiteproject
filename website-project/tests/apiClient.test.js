import apiClient from '../public/js/apiClient.js';

const runTests = async () => {
    const testSuite = {
        'Test GET Request': async () => {
            try {
                const response = await apiClient.request('/listings/search');
                console.assert(response.data, 'Test GET Request: PASSED');
            } catch (error) {
                console.error('Test GET Request: FAILED', error);
            }
        },
        'Test POST Request': async () => {
            try {
                const response = await apiClient.request('/users/ai-search', 'POST', { query: 'test' });
                console.assert(response, 'Test POST Request: PASSED');
            } catch (error) {
                console.error('Test POST Request: FAILED', error);
            }
        },
        'Test 404 Error': async () => {
            try {
                await apiClient.request('/non-existent-endpoint');
                console.error('Test 404 Error: FAILED - Expected an error to be thrown');
            } catch (error) {
                if (error.response && error.response.status === 404) {
                    console.log('Test 404 Error: PASSED');
                } else {
                    console.error('Test 404 Error: FAILED', error);
                }
            }
        },
        'Test Timeout': async () => {
            try {
                await apiClient.request('/listings/search', 'GET', null, { timeout: 1 });
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.log('Test Timeout: PASSED');
                } else {
                    console.error('Test Timeout: FAILED', error);
                }
            }
        }
    };

    for (const testName in testSuite) {
        await testSuite[testName]();
    }
};

runTests();
