drop procedure if exists 009_loginCreateAllowedForSeason;

delimiter $$
create procedure 009_loginCreateAllowedForSeason()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'season'
            and COLUMN_NAME = 'loginAllowed')
    then
        alter table season add column createAllowed tinyint default 1 after daysOfWeek;
        alter table season add column loginAllowed tinyint default 1 after daysOfWeek;
    end if;
end $$
delimiter ;

call 009_loginCreateAllowedForSeason();
drop procedure if exists 009_loginCreateAllowedForSeason;