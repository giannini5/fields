drop procedure if exists 026_addTournamentGamesToFlight;

delimiter $$
create procedure 026_addTournamentGamesToFlight()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'flight'
            and COLUMN_NAME = 'include5th6thGame')
    then
        alter table flight add COLUMN includeChampionshipGame   tinyint not NULL default 0 after name;
        alter table flight add COLUMN includeSemiFinalGames     tinyint not NULL default 0 after name;
        alter table flight add COLUMN include3rd4thGame         tinyint not NULL default 0 after name;
        alter table flight add COLUMN include5th6thGame         tinyint not NULL default 0 after name;
    end if;
end $$
delimiter ;

call 026_addTournamentGamesToFlight();
drop procedure if exists 026_addTournamentGamesToFlight;
