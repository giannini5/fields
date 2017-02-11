drop procedure if exists 022_addTitleToGame;

delimiter $$
create procedure 022_addTitleToGame()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'game'
            and COLUMN_NAME = 'flightId')
    then
        drop index ux_gameTimeTeamPool on game;

        alter table game add COLUMN flightId bigInt not NULL after id;
        alter table game add COLUMN title varchar(60) not NULL default '' after visitingTeamId;
        alter table game modify COLUMN poolId bigInt default NULL;
        alter table game modify COLUMN homeTeamId bigInt default NULL;
        alter table game modify COLUMN visitingTeamId bigInt default NULL;

        create unique index ux_gameTimeTeamFlight on game(flightId, gameTimeId, homeTeamId, visitingTeamId);
        create index ix_gameTimeTeamPool on game(poolId, gameTimeId, homeTeamId, visitingTeamId);
        create index ix_flightTitle on game(flightId, title);
    end if;
end $$
delimiter ;

call 022_addTitleToGame();
drop procedure if exists 022_addTitleToGame;
