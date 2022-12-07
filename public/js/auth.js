(function(root) 
{
	//FUNCTION DECLARATION
	var auth = {
		
		'setUserLevel' : setUserLevel,
		'getUserLevel' : getUserLevel,
		'setUserId' : setUserId,
		'getUserId' : getUserId,
		'isEqualOrUpperUserType' : isEqualOrUpperUserType,
		'getUserLevelName' : getUserLevelName,
	};
	
	root.auth = auth;

	var userLevel;

	function setUserLevel(level)
	{
		userLevel = level;
	}

	function getUserLevel()
	{
		return userLevel;
	}

	function setUserId(id)
	{
		userId = id;
	}

	function getUserId()
	{
		return userId;
	}

	function getUserLevelName()
	{
		if(userLevel == 0)
			return 'CA';
		else if(userLevel == 1)
			return 'SMA';
		else if(userLevel == 2)
			return 'MA';
		else if(userLevel == 3)
			return 'AG';
	}

	function isEqualOrUpperUserType(forLevel)
	{

		if(this.getUserLevel() <= forLevel)
			return true;

		return false;
	}


}(this));