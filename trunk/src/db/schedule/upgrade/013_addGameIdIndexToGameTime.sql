drop procedure if exists 012_addGameIdIndexToGameTime;

delimiter $$
create procedure 012_addGameIdIndexToGameTime()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'gameTime'
            and index_name = 'ux_gameId')
    then
        create unique index ux_gameId on gameTime(gameId);
    end if;
end $$
delimiter ;

call 012_addGameIdIndexToGameTime();
drop procedure if exists 012_addGameIdIndexToGameTime;
