drop procedure if exists 030_addVolunteerPointsToTeam;

delimiter $$
create procedure 030_addVolunteerPointsToTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and COLUMN_NAME = 'volunteerPoints')
    then
      alter table team add COLUMN volunteerPoints int not NULL default 0 after city;
    end if;
  end $$
delimiter ;

call 030_addVolunteerPointsToTeam();
drop procedure if exists 030_addVolunteerPointsToTeam;
