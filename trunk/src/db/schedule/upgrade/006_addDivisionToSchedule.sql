drop procedure if exists 006_addDivisionToSchedule;

delimiter $$
create procedure 006_addDivisionToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'divisionId')
    then
        alter table schedule add COLUMN divisionId bigint not null after id;
        create unique index ux_divisionIdName on schedule(divisionId, name);
        alter table schedule drop COLUMN poolId;
        drop index ux_poolName on schedule;
    end if;
end $$
delimiter ;

call 006_addDivisionToSchedule();
drop procedure if exists 006_addDivisionToSchedule;
