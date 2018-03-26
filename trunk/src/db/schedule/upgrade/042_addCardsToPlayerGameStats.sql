drop procedure if exists 042_addCardsToPlayerGameStats;

delimiter $$
create procedure 042_addCardsToPlayerGameStats()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'playerGameStats'
          and COLUMN_NAME = 'yellowCards')
    then
      alter table playerGameStats add COLUMN redCard int default 0 after keeperQuarter4;
      alter table playerGameStats add COLUMN yellowCards int default 0 after keeperQuarter4;
    end if;
  end $$
delimiter ;

call 042_addCardsToPlayerGameStats();
drop procedure if exists 042_addCardsToPlayerGameStats;