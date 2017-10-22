drop procedure if exists 036_addPlayInByWinToGame;

delimiter $$
create procedure 036_addPlayInByWinToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and COLUMN_NAME = 'playInHomeGameId')
    then
      alter table game add COLUMN playInHomeGameId int not NULL default 0 after title;
      alter table game add COLUMN playInVisitingGameId int not NULL default 0 after playInHomeGameId;
      alter table game add COLUMN playInByWin tinyint not NULL default 0 after playInVisitingGameId;

      create index ix_playInHomeByWin on game(playInByWin, playInHomeGameId);
      create index ix_playInVisitingByWin on game(playInByWin, playInVisitingGameId);
    end if;
  end $$
delimiter ;

call 036_addPlayInByWinToGame();
drop procedure if exists 036_addPlayInByWinToGame;
