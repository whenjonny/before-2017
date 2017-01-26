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

package org.smslib;

import java.util.*;

/**
 * This class represents a normal (text) outgoing / outbound message.
 * 
 * @see CWapSIMessage
 */
public class COutgoingMessage extends CMessage
{
	private static final long serialVersionUID = 1L;

	protected Date dispatchDate;

	private int validityPeriod;

	private boolean statusReport;

	private boolean flashSms;

	private int srcPort;

	private int dstPort;

	public COutgoingMessage()
	{
		super(CMessage.MessageType.Outgoing, null, null, null, null);

		validityPeriod = -1;
		statusReport = false;
		flashSms = false;
		srcPort = -1;
		dstPort = -1;
		dispatchDate = null;
		setDate(new Date());
	}

	/**
	 * General constructor for an outgoing message. Only the text and the recipient's number is required. The message encoding is set to 7bit by default.
	 * 
	 * @param recipient
	 *            The recipient's number - should be in international format.
	 * @param text
	 *            The message text.
	 * @see #setMessageEncoding(int)
	 * @see #setValidityPeriod(int)
	 * @see #setStatusReport(boolean)
	 * @see #setFlashSms(boolean)
	 * @see #setSourcePort(int)
	 * @see #setDestinationPort(int)
	 */
	public COutgoingMessage(String recipient, String text)
	{
		super(CMessage.MessageType.Outgoing, new Date(), null, recipient, text);

		validityPeriod = -1;
		statusReport = false;
		flashSms = false;
		srcPort = -1;
		dstPort = -1;
		dispatchDate = null;
		setDate(new Date());
	}

	protected boolean isBig() throws Exception
	{
		int messageLength;

		switch (getType())
		{
			case CMessage.MessageType.WapPushSI:
				messageLength = getPDUData().length() / 2;
				break;
			case CMessage.MessageType.Outgoing:
				messageLength = getText().length();
				break;
			default:
				throw new OopsException();
		}
		return (messageLength > maxSize() ? true : false);
	}

	protected int getNoOfParts() throws Exception
	{
		int noOfParts = 0;
		int partSize;
		int messageLength;

		partSize = maxSize() - 8;
		switch (getType())
		{
			case CMessage.MessageType.WapPushSI:
				messageLength = getPDUData().length() / 2;
				break;
			case CMessage.MessageType.Outgoing:
				messageLength = getText().length();
				break;
			default:
				throw new OopsException();
		}
		noOfParts = messageLength / partSize;
		if ((noOfParts * partSize) < (messageLength)) noOfParts++;
		return noOfParts;
	}

	private int maxSize() throws Exception
	{
		int size;

		switch (getMessageEncoding())
		{
			case CMessage.MessageEncoding.Enc7Bit:
				size = 160;
				break;
			case CMessage.MessageEncoding.Enc8Bit:
				size = 140;
				break;
			case CMessage.MessageEncoding.EncUcs2:
				size = 70;
				break;
			default:
				throw new OopsException();
		}
		return size;
	}

	private String getPart(String txt, int partNo, int udhLength) throws Exception
	{
		String textPart;
		int partSize;

		textPart = txt;
		if (partNo != 0)
		{
			partSize = maxSize() - udhLength;
			if (((partSize * (partNo - 1)) + partSize) > txt.length()) textPart = txt.substring(partSize * (partNo - 1));
			else textPart = txt.substring(partSize * (partNo - 1), (partSize * (partNo - 1)) + partSize);
		}
		return textPart;
	}

	private String getPDUPart(String txt, int partNo, int udhLength) throws Exception
	{
		String textPart;
		int partSize;

		textPart = txt;
		if (partNo != 0)
		{
			partSize = maxSize() - udhLength;
			partSize *= 2;
			if (((partSize * (partNo - 1)) + partSize) > txt.length()) textPart = txt.substring(partSize * (partNo - 1));
			else textPart = txt.substring(partSize * (partNo - 1), (partSize * (partNo - 1)) + partSize);
		}
		return textPart;
	}

	protected String getPDU(String smscNumber, int mpRefNo, int partNo) throws Exception
	{
		String pdu, udh;
		String str1, str2;
		int i, high, low, udhLength;
		char c;

		pdu = "";
		udh = "";
		if ((smscNumber != null) && (smscNumber.length() != 0))
		{
			str1 = "91" + toBCDFormat(smscNumber.substring(1));
			str2 = Integer.toHexString(str1.length() / 2);
			if (str2.length() != 2) str2 = "0" + str2;
			pdu = pdu + str2 + str1;
		}
		else if ((smscNumber != null) && (smscNumber.length() == 0)) pdu = pdu + "00";
		if (((srcPort != -1) && (dstPort != -1)) || (isBig()))
		{
			if (statusReport) pdu = pdu + "71";
			else pdu = pdu + "51";
		}
		else
		{
			if (statusReport) pdu = pdu + "31";
			else pdu = pdu + "11";
		}
		pdu = pdu + "00";
		str1 = getRecipient();
		if (str1.charAt(0) == '+')
		{
			str1 = toBCDFormat(str1.substring(1));
			str2 = Integer.toHexString(getRecipient().length() - 1);
			str1 = "91" + str1;
		}
		else
		{
			str1 = toBCDFormat(str1);
			str2 = Integer.toHexString(getRecipient().length());
			str1 = "81" + str1;
		}
		if (str2.length() != 2) str2 = "0" + str2;

		pdu = pdu + str2 + str1;
		pdu = pdu + "00";
		switch (getMessageEncoding())
		{
			case CMessage.MessageEncoding.Enc7Bit:
				if (flashSms) pdu = pdu + "10";
				else pdu = pdu + "00";
				break;
			case CMessage.MessageEncoding.Enc8Bit:
				if (flashSms) pdu = pdu + "14";
				else pdu = pdu + "04";
				break;
			case CMessage.MessageEncoding.EncUcs2:
				if (flashSms) pdu = pdu + "18";
				else pdu = pdu + "08";
				break;
			default:
				throw new OopsException();
		}

		pdu = pdu + getValidityPeriodBits();

		if ((srcPort != -1) && (dstPort != -1))
		{
			String s;

			udh += "060504";
			s = Integer.toHexString(dstPort);
			while (s.length() < 4)
				s = "0" + s;
			udh += s;
			s = Integer.toHexString(srcPort);
			while (s.length() < 4)
				s = "0" + s;
			udh += s;
		}

		if (isBig())
		{
			String s;

			if ((srcPort != -1) && (dstPort != -1)) udh = "0C" + udh.substring(2) + "0804";
			else udh += "060804";
			s = Integer.toHexString(mpRefNo);
			while (s.length() < 4)
				s = "0" + s;
			udh += s;
			s = Integer.toHexString(getNoOfParts());
			while (s.length() < 2)
				s = "0" + s;
			udh += s;
			s = Integer.toHexString(partNo);
			while (s.length() < 2)
				s = "0" + s;
			udh += s;
		}
		if ((udh.length() == 26) && (getMessageEncoding() == CMessage.MessageEncoding.Enc7Bit)) udh = udh + "00";
		udhLength = udh.length() / 2;

		switch (getMessageEncoding())
		{
			case CMessage.MessageEncoding.Enc7Bit:
				udhLength = udhLength * 8 / 7;
				str2 = textToPDU(getPart(getText(), partNo, udhLength));
				i = CGSMAlphabet.stringToBytes(getPart(getText(), partNo, udhLength), new byte[400]);
				str1 = Integer.toHexString(i + udhLength);
				break;
			case CMessage.MessageEncoding.Enc8Bit:
				switch (getType())
				{
					case CMessage.MessageType.Outgoing:
						str1 = getPart(getText(), partNo, udhLength);
						str2 = "";
						for (i = 0; i < str1.length(); i++)
						{
							c = str1.charAt(i);
							str2 = str2 + ((Integer.toHexString(c).length() < 2) ? "0" + Integer.toHexString(c) : Integer.toHexString(c));
						}
						str1 = Integer.toHexString(str1.length() + udhLength);
						break;
					case CMessage.MessageType.WapPushSI:
						str2 = getPDUPart(getPDUData(), partNo, udhLength);
						str1 = Integer.toHexString((str2.length() / 2) + udhLength);
						break;
					default:
						throw new OopsException();
				}
				break;
			case CMessage.MessageEncoding.EncUcs2:
				str1 = getPart(getText(), partNo, udhLength);
				str2 = "";
				for (i = 0; i < str1.length(); i++)
				{
					c = str1.charAt(i);
					high = c / 256;
					low = c % 256;
					str2 = str2 + ((Integer.toHexString(high).length() < 2) ? "0" + Integer.toHexString(high) : Integer.toHexString(high));
					str2 = str2 + ((Integer.toHexString(low).length() < 2) ? "0" + Integer.toHexString(low) : Integer.toHexString(low));
				}
				str1 = Integer.toHexString((str1.length() * 2) + udhLength);
				break;
			default:
				throw new OopsException();
		}
		if (str1.length() != 2) str1 = "0" + str1;
		if (udhLength != 0) pdu = pdu + str1 + udh + str2;
		else pdu = pdu + str1 + str2;
		return pdu.toUpperCase();
	}

	protected String getPDUData() throws Exception
	{
		throw new OopsException("The called method should be overriden!");
	}

	private String getValidityPeriodBits()
	{
		String bits;
		int value;

		if (validityPeriod == -1) bits = "FF";
		else
		{
			if (validityPeriod <= 12) value = (validityPeriod * 12) - 1;
			else if (validityPeriod <= 24) value = (((validityPeriod - 12) * 2) + 143);
			else if (validityPeriod <= 720) value = (validityPeriod / 24) + 166;
			else value = (validityPeriod / 168) + 192;
			bits = Integer.toHexString(value);
			if (bits.length() != 2) bits = "0" + bits;
			if (bits.length() > 2) bits = "FF";
		}
		return bits;
	}

	private String textToPDU(String txt) throws Exception
	{
		String pdu, str1;
		byte[] bytes, oldBytes, newBytes;
		BitSet bitSet;
		int i, j, value1, value2;

		bytes = new byte[400];
		i = CGSMAlphabet.stringToBytes(txt, bytes);
		oldBytes = new byte[i];
		for (j = 0; j < i; j++)
			oldBytes[j] = bytes[j];
		bitSet = new BitSet(oldBytes.length * 8);

		value1 = 0;
		for (i = 0; i < oldBytes.length; i++)
			for (j = 0; j < 7; j++)
			{
				value1 = (i * 7) + j;
				if ((oldBytes[i] & (1 << j)) != 0) bitSet.set(value1);
			}
		value1++;

		if (((value1 / 56) * 56) != value1) value2 = (value1 / 8) + 1;
		else value2 = (value1 / 8);
		if (value2 == 0) value2 = 1;

		newBytes = new byte[value2];
		for (i = 0; i < value2; i++)
			for (j = 0; j < 8; j++)
				if ((value1 + 1) > ((i * 8) + j)) if (bitSet.get(i * 8 + j)) newBytes[i] |= (byte) (1 << j);

		pdu = "";
		for (i = 0; i < value2; i++)
		{
			str1 = Integer.toHexString(newBytes[i]);
			if (str1.length() != 2) str1 = "0" + str1;
			str1 = str1.substring(str1.length() - 2, str1.length());
			pdu += str1;
		}
		return pdu;
	}

	private String toBCDFormat(String s)
	{
		String bcd;
		int i;

		if ((s.length() % 2) != 0) s = s + "F";
		bcd = "";
		for (i = 0; i < s.length(); i += 2)
			bcd = bcd + s.charAt(i + 1) + s.charAt(i);
		return bcd;
	}

	/**
	 * Returns the message recipient number. Number is in international format.
	 * 
	 * @return The Recipient's number.
	 */
	public String getRecipient()
	{
		return recipient;
	}

	/**
	 * Returns the defined validity period in hours.
	 * 
	 * @return The validity period (hours).
	 */
	public int getValidityPeriod()
	{
		return validityPeriod;
	}

	/**
	 * Returns the state of the delivery status report request.
	 * 
	 * @return True if delivery status report request is enabled.
	 * @see #setValidityPeriod(int)
	 */
	public boolean getStatusReport()
	{
		return statusReport;
	}

	/**
	 * Returns true if the SMS is a flash SMS.
	 * 
	 * @return True if the SMS is a flash SMS.
	 * @see #setFlashSms(boolean)
	 */
	public boolean getFlashSms()
	{
		return flashSms;
	}

	/**
	 * Return the message source-port information. Returns -1 if the source-port is undefined.
	 * 
	 * @return The message source-port information.
	 * @see #setSourcePort(int)
	 * @see #getDestinationPort()
	 */
	public int getSourcePort()
	{
		return srcPort;
	}

	/**
	 * Return the message destination-port information. Returns -1 if the destination-port is undefined.
	 * 
	 * @return The message destination-port information.
	 * @see #setDestinationPort(int)
	 * @see #getSourcePort()
	 */
	public int getDestinationPort()
	{
		return dstPort;
	}

	/**
	 * Returns the date of dispatch - the date when this message was send from SMSLib. Returns NULL if the message has not been sent yet.
	 * 
	 * @return The dispatch date.
	 */
	public Date getDispatchDate()
	{
		return (Date) dispatchDate.clone();
	}

	/**
	 * Sets the Recipient's number. The number should be in international format.
	 * 
	 * @param recipient
	 *            The Recipient's number.
	 */
	public void setRecipient(String recipient)
	{
		this.recipient = recipient;
	}

	/**
	 * Sets the validity period. By default, an outgoing message has the maximum allowed validity period.
	 * 
	 * @param hours
	 *            The validity period in hours.
	 */
	public void setValidityPeriod(int hours)
	{
		this.validityPeriod = hours;
	}

	/**
	 * Sets the delivery status report functionality. Set this to true if you want to enable delivery status report for this specific message.
	 * 
	 * @param statusReport
	 *            True if you want to enable delivery status reports.
	 */
	public void setStatusReport(boolean statusReport)
	{
		this.statusReport = statusReport;
	}

	/**
	 * Set the Flash SMS indication.
	 * <p>
	 * Flash SMS appear directly on recipient's screen. This functionality may not be supported on all headsets.
	 * 
	 * @param flashSms
	 *            True if you want to send a Flash SMS.
	 */
	public void setFlashSms(boolean flashSms)
	{
		this.flashSms = flashSms;
	}

	/**
	 * Sets the Source Port information field. This settings affects PDU header creation.
	 * 
	 * @param port
	 *            The Source Port.
	 */
	public void setSourcePort(int port)
	{
		this.srcPort = port;
	}

	/**
	 * Sets the DestinationPort information field. This settings affects PDU header creation.
	 * 
	 * @param port
	 *            The Destination Port.
	 */
	public void setDestinationPort(int port)
	{
		this.dstPort = port;
	}
}
