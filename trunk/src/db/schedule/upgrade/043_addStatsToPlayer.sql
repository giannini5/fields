drop procedure if exists 043_addStatsToPlayer;

delimiter $$
create procedure 043_addStatsToPlayer()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'player'
          and COLUMN_NAME = 'goals')
    then
      alter table player add COLUMN yellowCards int default 0 after number;
      alter table player add COLUMN redCards int default 0 after number;
      alter table player add COLUMN quartersKeep int default 0 after number;
      alter table player add COLUMN quartersSub int default 0 after number;
      alter table player add COLUMN goals int default 0 after number;
    end if;
  end $$
delimiter ;

call 043_addStatsToPlayer();
drop procedure if exists 043_addStatsToPlayer;