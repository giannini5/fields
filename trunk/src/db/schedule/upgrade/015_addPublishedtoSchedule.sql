drop procedure if exists 015_addPublishedToSchedule;

delimiter $$
create procedure 015_addPublishedToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'published')
    then
        alter table schedule add COLUMN published tinyint NOT NULL default 0 after daysOfWeek;
        create index ix_divisionPublished on schedule(divisionId, published);
    end if;
end $$
delimiter ;

call 015_addPublishedToSchedule();
drop procedure if exists 015_addPublishedToSchedule;
