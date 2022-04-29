namespace php NetricApi
namespace js NetricApi

/**
 * Invalid arguments sent
 */
exception InvalidArgument {
  1: string message
}

/**
 * Thrown when an error takes place that is not expected
 */
exception ErrorException {
    1: string message
}

/**
 * Authentication service
 */
service Authentication
{
    /**
     * Check if a given auth token is valid
     */
    bool isTokenValid(1:string token);
}

/**
 * Test service used for ping and healthchecks
 */
service Test
{
    /**
     * Returns with a simple "hello"
     */
    string ping();
}


/**
 * SERVICE: Entity
 * ==========================================================
 */

/**
 * Entity service
 */
service Entity
{
    /**
     * Indicate that an entity has been seen by a given user
     */
    void setEntitySeenBy(1:string entityId, 2:string userId, 3:string accountId);

    /**
     * Update the user last active
     */
    void updateUserLastActive(1:string userId, 2:string accountId, 3:string timestamp)
        throws (1:ErrorException error, 2:InvalidArgument badRequest);
}


/**
 * SERVICE: Chat
 * ==========================================================
 */

/**
 * Chat service used for chat-specific operations
 */
service Chat
{
    /**
     * Notify any members who were not in a room when a new message is sent
     */
    void notifyAbsentOfNewMessage(1:string messageId, 3:string accountId);
}

/**
 * SERVICE: Worker
 * ==========================================================
 */

/**
 * The worker service handles processing queued jobs
 */
service Worker
{
    /**
     * Process a background job
     */
    bool process(1:string workerName, 2:string jsonPayload) 
        throws (1:ErrorException error, 2:InvalidArgument badRequest) ;
}

/**
 * SERVICE: EntityQuery
 * ==========================================================
 */

// /**
//  * Define query type data
//  */
// struct EntityQueryData {
//     1: string obj_type;
//     2: i32 offset;
//     3: i32 limit;
// }

// /**
//  * Define what an entity query result will look like
//  */
// struct EntityQueryResultData {
//     1: EntityQueryData query_ran;
//     2: string account;
//     3: i32 num;
//     4: i32 total_num;
//     5: list<map<string,string>> entities
// }

/**
 * Entity query service
 */
service EntityQuery
{
    /**
     * Update the user last active
     */
    string execute(1:string userId, 2:string accountId, 3:string jsonQuery) 
        throws (1:ErrorException error, 2:InvalidArgument badRequest);
}
