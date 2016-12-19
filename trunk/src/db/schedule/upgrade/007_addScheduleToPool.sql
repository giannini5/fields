drop procedure if exists 007_addScheduleToPool;

delimiter $$
create procedure 007_addScheduleToPool()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'pool'
            and COLUMN_NAME = 'scheduleId')
    then
        alter table pool add COLUMN scheduleId bigint not null after divisionId;
        alter table pool drop COLUMN divisionId;
        drop index ux_divisionName on pool;
        create unique index ux_scheduleName on pool(scheduleId, name);
    end if;
end $$
delimiter ;

call 007_addScheduleToPool();
drop procedure if exists 007_addScheduleToPool;
