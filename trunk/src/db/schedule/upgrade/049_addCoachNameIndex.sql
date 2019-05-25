drop procedure if exists 049_addCoachNameIndex;

delimiter $$
create procedure 049_addCoachNameIndex()
  begin

    if not exists(
        select
          *
        from
          information_schema.statistics
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'coach'
          and index_name = 'ix_name')
    then
      create index ix_name on coach(name);
    end if;
  end $$
delimiter ;

call 049_addCoachNameIndex();
drop procedure if exists 049_addCoachNameIndex;