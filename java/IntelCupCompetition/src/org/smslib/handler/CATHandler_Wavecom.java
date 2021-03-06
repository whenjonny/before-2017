// SMSLib for Java
// An open-source API Library for sending and receiving SMS via a GSM modem.
// Copyright (C) 2002-2006, Thanasis Delenikas, Athens/GREECE
// Web Site: http://www.smslib.org
//
// SMSLib is distributed under the LGPL license.
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
// 
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
// Lesser General Public License for more details.
// 
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

package org.smslib.handler;

import org.smslib.*;
import org.apache.log4j.*;

public class CATHandler_Wavecom extends CATHandler
{
	public CATHandler_Wavecom(CSerialDriver serialDriver, Logger log, CService srv)
	{
		super(serialDriver, log, srv);
	}

	protected void reset() throws Exception
	{
		/*
		 * serialDriver.send("AT+CFUN=1\r"); Thread.sleep(20000);
		 * serialDriver.clearBuffer();
		 */
	}

	protected void init() throws Exception
	{
		serialDriver.send("AT+WOPEN=0\r");
		serialDriver.getResponse();
	}

	protected boolean enableIndications() throws Exception
	{
		serialDriver.send("AT+CNMI=1,1,0,2,0\r");
		return (serialDriver.getResponse().matches("\\s+OK\\s+"));
	}

	protected boolean disableIndications() throws Exception
	{
		serialDriver.send("AT+CNMI=0,0,0,2,0\r");
		return (serialDriver.getResponse().matches("\\s+OK\\s+"));
	}

}
