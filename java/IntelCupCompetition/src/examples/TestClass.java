//
// TestClass.java - Generic test template class.
//

package examples;

import org.smslib.*;

class TestClass
{
	public static void main(String[] args)
	{
		CService srv = new CService("COM1", 57600, "Nokia", "");

		System.out.println();
		System.out.println("ReadMessages: Synchronous Reading.");
		System.out.println("  Using " + CService._name + " " + CService._version);
		System.out.println();

		try
		{
			srv.setSimPin("0000");

			// OK, let connect and see what happens... Exceptions may be thrown
			// here!
			srv.connect();

			// Lets get info about the GSM device...
			System.out.println("Mobile Device Information: ");
			System.out.println("	Manufacturer  : " + srv.getDeviceInfo().getManufacturer());
			System.out.println("	Model         : " + srv.getDeviceInfo().getModel());
			System.out.println("	Serial No     : " + srv.getDeviceInfo().getSerialNo());
			System.out.println("	IMSI          : " + srv.getDeviceInfo().getImsi());
			System.out.println("	S/W Version   : " + srv.getDeviceInfo().getSwVersion());
			System.out.println("	Battery Level : " + srv.getDeviceInfo().getBatteryLevel() + "%");
			System.out.println("	Signal Level  : " + srv.getDeviceInfo().getSignalLevel() + "%");
			System.out.println("	GPRS Status   : " + (srv.getDeviceInfo().getGprsStatus() ? "Enabled" : "Disabled"));
			System.out.println("");

			// Write your test calls here.
			// ...
			// ...
			//COutgoingMessage msg = new COutgoingMessage("+...", "83|1|W83|20.11.2006 15.27|1|F|SMSLib is an API library which allows you to send and receive SMS messages via your GSM modem. You can use SMSLib either with a dedicated GSM modem or with a GSM phone that complies with some standard. I will use the term \"GSM modem\" in this tutorial, but the same things apply to GSM phones as well.|5|8|");
			//COutgoingMessage msg = new COutgoingMessage("+...", "012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789");
			//CWapSIMessage msg = new CWapSIMessage("+...", new URL("https://mail.google.com/"), "Visit GMail now!Visit GMail now!Visit GMail now!Visit GMail now!Visit GMail now!Visit GMail now!...x.......1........2........3.......4.........5........6");
			//msg.setMessageEncoding(CMessage.MessageEncoding.EncUcs2);
			//msg.setSourcePort(5000);
			//msg.setDestinationPort(5000);
			//srv.sendMessage(msg);


			srv.disconnect();
		}
		catch (Exception e)
		{
			e.printStackTrace();
		}
		System.exit(0);
	}
}
