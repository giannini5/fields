drop procedure if exists 012_addGamesPerTeamToSchedule;

delimiter $$
create procedure 012_addGamesPerTeamToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'gamesPerTeam')
    then
        alter table schedule add COLUMN gamesPerTeam int not null after name;
    end if;
end $$
delimiter ;

call 012_addGamesPerTeamToSchedule();
drop procedure if exists 012_addGamesPerTeamToSchedule;
