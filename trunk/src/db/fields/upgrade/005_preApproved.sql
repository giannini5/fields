drop procedure if exists 005_preApproved;

delimiter $$
create procedure 005_preApproved()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'facility'
            and COLUMN_NAME = 'preApproved')
    then
        alter table facility add column preApproved tinyint not NULL default 1 after image;
    end if;
end $$
delimiter ;

call 005_preApproved();
drop procedure if exists 005_preApproved;