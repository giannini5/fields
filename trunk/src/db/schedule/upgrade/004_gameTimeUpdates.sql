drop procedure if exists 004_gameTimeUpdates;

delimiter $$
create procedure 004_gameTimeUpdates()
begin

    if exists(
      SELECT
          *
      FROM
          information_schema.statistics
      WHERE
          table_schema = DATABASE()
          and table_name = 'gameTime'
          and COLUMN_NAME = 'divisionId'
    )
    then
        alter table gameTime drop column divisionId;
        drop index ux_gameDateFieldTime on gameTime;
    end if;
end $$
delimiter ;

call 004_gameTimeUpdates();
drop procedure if exists 004_gameTimeUpdates;
