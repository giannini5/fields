drop procedure if exists 044_addMoreStatsToPlayer;

delimiter $$
create procedure 044_addMoreStatsToPlayer()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'player'
          and COLUMN_NAME = 'quartersAbsent')
    then
      alter table player add COLUMN quartersAbsent int default 0 after quartersKeep;
      alter table player add COLUMN quartersInjured int default 0 after quartersKeep;
    end if;
  end $$
delimiter ;

call 044_addMoreStatsToPlayer();
drop procedure if exists 044_addMoreStatsToPlayer;