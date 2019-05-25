drop procedure if exists 053_addRefereeCrewIdToGame;

delimiter $$
create procedure 053_addRefereeCrewIdToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and COLUMN_NAME = 'refereeCrewId')
    then
      alter table game add COLUMN refereeCrewId bigint default NULL after locked;
    end if;
  end $$
delimiter ;

call 053_addRefereeCrewIdToGame();
drop procedure if exists 053_addRefereeCrewIdToGame;