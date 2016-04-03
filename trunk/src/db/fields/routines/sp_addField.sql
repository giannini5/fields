drop procedure if exists sp_addField;

delimiter $$

create procedure sp_addField(in _leagueName   varchar(60),
                             in _facilityName varchar(60),
                             in _name         varchar(60),
                             in _enabled      tinyint)
BEGIN
    declare l_leagueId   bigInt default NULL;
    declare l_facilityId bigInt default NULL;

    select l.id into l_leagueId from league as l where l.name = _leagueName;
    if (l_leagueId is NULL) then
        set @errorMessage = concat("ABORT: league not found for : ", _leagueName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_facilityId from facility as f where f.leagueId = l_leagueId and f.name = _facilityName;
    if (l_facilityId is NULL) then
        set @errorMessage = concat("ABORT: facility not found for : ", _facilityName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    insert into field(
        facilityId,
        name,
        enabled)
    values (
        l_facilityId,
        _name,
        _enabled)
    on duplicate key update
        enabled = _enabled;
END $$

delimiter ;
