drop procedure if exists 024_addLockedToGame;

delimiter $$
create procedure 024_addLockedToGame()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'game'
            and COLUMN_NAME = 'locked')
    then
        alter table game add COLUMN locked tinyint not NULL default 0 after title;
    end if;
end $$
delimiter ;

call 024_addLockedToGame();
drop procedure if exists 024_addLockedToGame;
