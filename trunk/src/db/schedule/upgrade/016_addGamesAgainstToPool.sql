drop procedure if exists 016_addGamesAgainstToPool;

delimiter $$
create procedure 016_addGamesAgainstToPool()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'pool'
            and COLUMN_NAME = 'gamesAgainstPoolId')
    then
        alter table pool add COLUMN gamesAgainstPoolId bigint default null after name;
    end if;
end $$
delimiter ;

call 016_addGamesAgainstToPool();
drop procedure if exists 016_addGamesAgainstToPool;
