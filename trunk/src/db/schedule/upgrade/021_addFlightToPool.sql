drop procedure if exists 021_addFlightToPool;

delimiter $$
create procedure 021_addFlightToPool()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'pool'
            and COLUMN_NAME = 'flightId')
    then
        alter table pool add COLUMN flightId bigInt NOT NULL after id;
        create unique index ux_flightName on pool(flightId, name);
    end if;
end $$
delimiter ;

call 021_addFlightToPool();
drop procedure if exists 021_addFlightToPool;
