/**
 * Define query type data
 */
struct EntityQueryData {
    1: string obj_type;
    2: int32 offset;
    3: int32 limit;
}

/**
 * Define what an entity query result will look like
 */
struct EntityQueryResultData {
    1: EntityQueryData query_ran;
    2: string account;
    3: int32 num;
    4: int32 total_num;
}

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
