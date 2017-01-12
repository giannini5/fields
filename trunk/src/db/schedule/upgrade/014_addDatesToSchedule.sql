drop procedure if exists 014_addDatesToSchedule;

delimiter $$
create procedure 014_addDatesToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'startDate')
    then
        alter table schedule add COLUMN startDate date NOT NULL after gamesPerTeam;
        alter table schedule add COLUMN endDate date NOT NULL after startDate;
        alter table schedule add COLUMN daysOfWeek char(8) NOT NULL DEFAULT '0000011' after endDate;
    end if;
end $$
delimiter ;

call 014_addDatesToSchedule();
drop procedure if exists 014_addDatesToSchedule;
