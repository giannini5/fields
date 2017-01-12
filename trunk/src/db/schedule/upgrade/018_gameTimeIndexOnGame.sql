drop procedure if exists 018_gameTimeIndexOnGame;

delimiter $$
create procedure 018_gameTimeIndexOnGame()
begin

    if exists(
      SELECT
          *
      FROM
          information_schema.statistics
      WHERE
          table_schema = DATABASE()
          and table_name = 'game'
          and index_name = 'ux_oneGame'
    )
    then
        drop index ux_oneGame on game;
        create index ix_gameDate on game(gameTimeId);
    end if;
end $$
delimiter ;

call 018_gameTimeIndexOnGame();
drop procedure if exists 018_gameTimeIndexOnGame;
