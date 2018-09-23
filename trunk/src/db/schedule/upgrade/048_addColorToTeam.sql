drop procedure if exists 048_addColorToTeam;

delimiter $$
create procedure 048_addColorToTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and COLUMN_NAME = 'color')
    then
      alter table team add COLUMN color varchar(24) not NULL default '' after nameId;
    end if;
  end $$
delimiter ;

call 048_addColorToTeam();
drop procedure if exists 048_addColorToTeam;