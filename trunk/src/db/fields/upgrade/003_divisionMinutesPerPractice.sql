drop procedure if exists 003_minutesPerPractice;

delimiter $$
create procedure 003_minutesPerPractice()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'division'
            and COLUMN_NAME = 'maxMinutesPerPractice')
    then
        alter table division add column maxMinutesPerPractice int not NULL default 60 after name;
        alter table division add column maxMinutesPerWeek int not NULL default 120 after maxMinutesPerPractice;
    end if;
end $$
delimiter ;

call 003_minutesPerPractice();
drop procedure if exists 003_minutesPerPractice;