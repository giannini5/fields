drop procedure if exists 002_addPhone2ToFamily;

delimiter $$
create procedure 002_addPhone2ToFamily()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'family'
            and COLUMN_NAME = 'phone2')
    then
        drop table family;
        create table family (
          id           bigint auto_increment,
          seasonId     bigint not NULL,
          phone1       varchar(128) not NULL,
          phone2       varchar(128) not NULL default '',
          PRIMARY KEY (id, seasonId, phone1, phone2),
          UNIQUE KEY ux_seasonPhone (seasonId, phone1, phone2)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        create index ix_familyId on coach(familyId);
        create index ix_familyId on assistantCoach(familyId);
    end if;
end $$
delimiter ;

call 002_addPhone2ToFamily();
drop procedure if exists 002_addPhone2ToFamily;
