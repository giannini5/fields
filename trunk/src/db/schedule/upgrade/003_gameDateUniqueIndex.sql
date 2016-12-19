drop procedure if exists 003_gameDateUniqueIndex;

delimiter $$
create procedure 003_gameDateUniqueIndex()
begin

    if exists(
      SELECT
          *
      FROM
          information_schema.statistics
      WHERE
          table_schema = DATABASE()
          and table_name = 'gameDate'
          and index_name = 'ix_seasonDay'
    )
    then
        drop index ix_seasonDay on gameDate;
        create unique index ux_seasonDay on gameDate(seasonId, day);
    end if;
end $$
delimiter ;

call 003_gameDateUniqueIndex();
drop procedure if exists 003_gameDateUniqueIndex;
