drop procedure if exists 010_addGameDurationMinutesToDivision;

delimiter $$
create procedure 010_addGameDurationMinutesToDivision()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'division'
            and COLUMN_NAME = 'gameDurationMinutes')
    then
        alter table division add COLUMN gameDurationMinutes int not null after gender;
    end if;
end $$
delimiter ;

call 010_addGameDurationMinutesToDivision();
drop procedure if exists 010_addGameDurationMinutesToDivision;
