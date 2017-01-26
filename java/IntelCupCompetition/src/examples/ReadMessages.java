// ReadMessages.java - Sample application.
//
// This application shows you the basic procedure needed for reading
// SMS messages from your GSM modem, in synchronous mode.
//

package examples;

import java.util.LinkedList;
import java.util.List;

import org.smslib.CIncomingMessage;
import org.smslib.CService;

class ReadMessages
{
	public List getMessage(){

		LinkedList msgList = new LinkedList();

		// Define the CService object. The parameters show the Comm Port used,
		// the Baudrate, the Manufacturer and Model strings. Manufacturer and
		// Model strings define which of the available AT Handlers will be used.
		CService srv = new CService("COM2", 57600, "GSM", "");
/*
		System.out.println();
		System.out.println("ReadMessages: Synchronous Reading.");
		System.out.println("  Using " + CService._name + " " + CService._version);
		System.out.println();
*/
		try
		{
			// If the GSM device is PIN protected, enter the PIN here.
			// PIN information will be used only when the GSM device reports
			// that it needs
			// a PIN in order to continue.
			srv.setSimPin("0000");

			//	If you would like to change the protocol to TEXT, do it here!
			// srv.setProtocol(CService.Protocol.TEXT);

			// OK, let connect and see what happens... Exceptions may be thrown
			// here!
			srv.connect();
/*
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
*/
			// Get the messages in a LinkedList.
			// The Class defines which messages should be read. Here we request
			// the readout of all messages, whether read or unread.
			srv.readMessages(msgList, CIncomingMessage.MessageClass.All);
			
			// Iterate and display.
			// The CMessage parent object has a toString() method which displays
			// all of its contents. Useful for debugging, but for a real world
			// application you should use the necessary getXXX methods.
/*
			for (int i = 0; i < msgList.size(); i++)
			{
				CIncomingMessage msg = (CIncomingMessage) msgList.get(i);
				System.out.println(msg);
	            //System.out.println("发信人："  + msg.getOriginator() +  " 短信内容:"   
	             //       + msg.getText());
	            System.out.println();
			}

			// Disconnect - Don't forget to disconnect!
*/
			
			srv.disconnect();
		}
		catch (Exception e)
		{
			e.printStackTrace();
		}
		//System.exit(0);
		return msgList;		
	}
	
	public static void main(String[] args)
	{	
		ReadMessages msg = new ReadMessages();
		List list = msg.getMessage();
		//System.out.println(list.toString());
		for (int i = 0; i < list.size(); i++)
		{
			CIncomingMessage _msg = (CIncomingMessage) list.get(i);
			System.out.println("id:" + i);
			System.out.println(_msg);
			//System.out.println("发信人："  + _msg.getOriginator() +  " 短信内容:"   
            //       + _msg.getText());
            //System.out.println();
		}
		
	}
}
