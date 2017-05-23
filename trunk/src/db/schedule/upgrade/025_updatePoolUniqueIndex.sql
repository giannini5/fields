drop procedure if exists 025_updaetPoolUniqueIndex;

delimiter $$
create procedure 025_updaetPoolUniqueIndex()
begin

    if exists(
      SELECT
          *
      FROM
          information_schema.statistics
      WHERE
          table_schema = DATABASE()
          and table_name  = 'pool'
          and index_name = 'ux_scheduleName'
    )
    then
        drop index ux_scheduleName on pool;
        create unique index ux_scheduleFlightName on pool(scheduleId, flightId, name);
    end if;
end $$
delimiter ;

call 025_updaetPoolUniqueIndex();
drop procedure if exists 025_updaetPoolUniqueIndex;