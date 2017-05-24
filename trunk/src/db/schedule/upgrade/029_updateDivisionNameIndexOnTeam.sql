drop procedure if exists 029_updateDivisionNameIndexOnTeam;

delimiter $$
create procedure 029_updateDivisionNameIndexOnTeam()
  begin

    if not exists(
        select
          *
        from
          information_schema.statistics
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'team'
          and index_name = 'ux_divisionName')
    then
      drop index ux_divisionName on team;
      create unique index ux_divisionNameIdName on gameDate(divisionId, nameId, name);
    end if;
  end $$
delimiter ;

call 029_updateDivisionNameIndexOnTeam();
drop procedure if exists 029_updateDivisionNameIndexOnTeam;
