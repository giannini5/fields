drop procedure if exists 008_replaceScheduleWithPoolForGame;

delimiter $$
create procedure 008_replaceScheduleWithPoolForGame()
begin

    if not exists(
        select
            *
        from
            information_schema.statistics
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'game'
            and COLUMN_NAME = 'poolId')
    then
        alter table game add COLUMN poolId bigint not null after scheduleId;
        alter table game drop COLUMN scheduleId;
        create unique index ux_gameTimeTeamPool on game (poolId, gameTimeId, homeTeamId, visitingTeamId);
    end if;
end $$
delimiter ;

call 008_replaceScheduleWithPoolForGame();
drop procedure if exists 008_replaceScheduleWithPoolForGame;
