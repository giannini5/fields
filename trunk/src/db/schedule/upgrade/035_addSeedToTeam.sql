drop procedure if exists 035_addSeedToTeam;

delimiter $$
create procedure 035_addSeedToTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and COLUMN_NAME = 'seed')
    then
      alter table team add COLUMN seed int not NULL default 0 after volunteerPoints;
    end if;
  end $$
delimiter ;

call 035_addSeedToTeam();
drop procedure if exists 035_addSeedToTeam;
