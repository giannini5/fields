drop procedure if exists 045_addInjuredAbsentToPlayerGameStats.sql;

delimiter $$
create procedure 045_addInjuredAbsentToPlayerGameStats()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'playerGameStats'
          and COLUMN_NAME = 'injuredQuarter1')
    then
      alter table playerGameStats add COLUMN absentQuarter4 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN absentQuarter3 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN absentQuarter2 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN absentQuarter1 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN injuredQuarter4 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN injuredQuarter3 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN injuredQuarter2 int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN injuredQuarter1 int default 0 after keeperQuarter4;
    end if;
  end $$
delimiter ;

call 045_addInjuredAbsentToPlayerGameStats();
drop procedure if exists 045_addInjuredAbsentToPlayerGameStats;