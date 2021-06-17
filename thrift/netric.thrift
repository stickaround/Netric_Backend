namespace php NetricApi
namespace js NetricApi

service Authentication
{
    /**
     * Check if a given auth token is valid
     */
    bool isTokenValid(1:string token);
}

service Test
{
    /**
     * Returns with a simple "hello"
     */
    string ping();
}