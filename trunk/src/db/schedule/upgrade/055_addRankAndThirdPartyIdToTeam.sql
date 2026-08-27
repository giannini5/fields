drop procedure if exists 055_addRankAndThirdPartyIdToTeam;

delimiter $$
create procedure 055_addRankAndThirdPartyIdToTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and COLUMN_NAME = 'rank')
    then
      alter table team add COLUMN rank int not NULL default 1000 after seed;
      alter table team add COLUMN thirdPartyId varchar(128) default NULL after rank;
      create unique index ux_thirdPartyId on team(thirdPartyId);
    end if;

  end $$
delimiter ;

call 055_addRankAndThirdPartyIdToTeam();
drop procedure if exists 055_addRankAndThirdPartyIdToTeam;
