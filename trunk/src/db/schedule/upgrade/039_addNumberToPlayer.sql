drop procedure if exists 039_addNumberToPlayer;

delimiter $$
create procedure 039_addNumberToPlayer()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'player'
          and COLUMN_NAME = 'number')
    then
      alter table player add COLUMN number int default null after phone;
    end if;
  end $$
delimiter ;

call 039_addNumberToPlayer();
drop procedure if exists 039_addNumberToPlayer;
