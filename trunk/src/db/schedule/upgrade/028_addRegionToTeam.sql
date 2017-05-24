drop procedure if exists 028_addRegionToTeam;

delimiter $$
create procedure 028_addRegionToTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and COLUMN_NAME = 'region')
    then
      alter table team add COLUMN nameId varchar(10) not NULL default '' after name;
      alter table team add COLUMN region varchar(60) not NULL default '' after nameId;
      alter table team add COLUMN city varchar(60) not NULL default '' after region;
    end if;
  end $$
delimiter ;

call 028_addRegionToTeam();
drop procedure if exists 028_addRegionToTeam;
