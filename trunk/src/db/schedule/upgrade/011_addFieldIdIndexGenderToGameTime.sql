drop procedure if exists 011_addFieldIdIndexGenderToGameTime;

delimiter $$
create procedure 011_addFieldIdIndexGenderToGameTime()
begin

    if not exists(
      SELECT
          *
      FROM
          information_schema.columns
      WHERE
          table_schema = DATABASE()
          and table_name  = 'gameTime'
          and column_name = 'genderPreference'
    )
    then
        alter table gameTime add COLUMN genderPreference varchar(10) not null after startTime;
        create index ix_fieldIdStartTime on gameTime(fieldId, startTime);
        create index ix_fieldIdGender on gameTime(fieldId, genderPreference);
    end if;
end $$
delimiter ;

call 011_addFieldIdIndexGenderToGameTime();
drop procedure if exists 011_addFieldIdIndexGenderToGameTime;