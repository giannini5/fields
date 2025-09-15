drop procedure if exists 054_addThirdPartyGameId;

delimiter $$
create procedure 054_addThirdPartyGameId()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and COLUMN_NAME = 'thirdPartyGameId')
    then
      alter table game add COLUMN thirdPartyGameId varchar(64) default NULL after locked;
      alter table game add key ix_thirdPartyGameId(thirdPartyGameId);
    end if;
  end $$
delimiter ;

call 054_addThirdPartyGameId();
drop procedure if exists 054_addThirdPartyGameId;