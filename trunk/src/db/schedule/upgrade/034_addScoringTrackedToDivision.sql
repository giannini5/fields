drop procedure if exists 034_addScoringTrackedToDivision;

delimiter $$
create procedure 034_addScoringTrackedToDivision()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'division'
          and COLUMN_NAME = 'scoringTracked')
    then
      alter table division add COLUMN scoringTracked int default 1 after gameDurationMinutes;
    end if;
  end $$
delimiter ;

call 034_addScoringTrackedToDivision();
drop procedure if exists 034_addScoringTrackedToDivision;
