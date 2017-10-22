drop procedure if exists 037_addCombineLeagueSchedulesToDivision;

delimiter $$
create procedure 037_addCombineLeagueSchedulesToDivision()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'division'
          and COLUMN_NAME = 'combineLeagueSchedules')
    then
      alter table division add COLUMN combineLeagueSchedules tinyint not NULL default 0 after displayOrder;
    end if;
  end $$
delimiter ;

call 037_addCombineLeagueSchedulesToDivision();
drop procedure if exists 037_addCombineLeagueSchedulesToDivision;
