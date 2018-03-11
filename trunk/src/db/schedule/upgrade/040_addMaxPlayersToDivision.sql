drop procedure if exists 040_addMaxPlayersToDivision;

delimiter $$
create procedure 040_addMaxPlayersToDivision()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'division'
          and COLUMN_NAME = 'maxPlayersPerTeam')
    then
      alter table division add COLUMN maxPlayersPerTeam int default 22 after gender;
    end if;
  end $$
delimiter ;

call 040_addMaxPlayersToDivision();
drop procedure if exists 040_addMaxPlayersToDivision;
