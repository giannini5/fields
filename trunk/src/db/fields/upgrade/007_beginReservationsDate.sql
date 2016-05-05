drop procedure if exists 007_beginReservationsDate;

delimiter $$
create procedure 007_beginReservationsDate()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'season'
            and COLUMN_NAME = 'beginReservationsDate')
    then
        alter table season add column beginReservationsDate dateTime  default '2016-07-15' after name;
    end if;
end $$
delimiter ;

call 007_beginReservationsDate();
drop procedure if exists 007_beginReservationsDate;