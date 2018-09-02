drop procedure if exists 047_addLockedToGameTime;

delimiter $$
create procedure 047_addLockedToGameTime()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'gameTime'
          and COLUMN_NAME = 'locked')
    then
      alter table gameTime add COLUMN locked tinyint not NULL default 0 after gameId;
    end if;
  end $$
delimiter ;

call 047_addLockedToGameTime();
drop procedure if exists 047_addLockedToGameTime;