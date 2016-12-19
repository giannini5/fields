drop procedure if exists 001_addGenderToDivision;

delimiter $$
create procedure 001_addGenderToDivision()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'division'
            and COLUMN_NAME = 'gender')
    then
        alter table division add COLUMN gender VARCHAR(20) not null default '';
        alter table division add COLUMN displayOrder int not null default 0;
        drop index ux_leagueName on division;
        create unique index ux_leagueNameGender on division(seasonId, name, gender);
    end if;
end $$
delimiter ;

call 001_addGenderToDivision();
drop procedure if exists 001_addGenderToDivision;
