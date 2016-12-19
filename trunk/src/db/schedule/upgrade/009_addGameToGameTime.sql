drop procedure if exists 009_addGameToGameTime;

delimiter $$
create procedure 009_addGameToGameTime()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'gameTime'
            and COLUMN_NAME = 'gameId')
    then
        alter table gameTime add COLUMN gameId bigint default null after startTime;
    end if;
end $$
delimiter ;

call 009_addGameToGameTime();
drop procedure if exists 009_addGameToGameTime;
