drop procedure if exists 001_divisionFieldIndex;

delimiter $$
create procedure 001_divisionFieldIndex()
begin

    if not exists(
        select 
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'divisionField'
            and index_name = 'ix_facilityField')
    then
        alter table divisionField add index ix_facilityField(facilityId, fieldId);
    end if;
end $$
delimiter ;

call 001_divisionFieldIndex();
drop procedure if exists 001_divisionFieldIndex;
