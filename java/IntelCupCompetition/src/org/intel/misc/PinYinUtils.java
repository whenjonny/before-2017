package org.intel.misc;

import net.sourceforge.pinyin4j.PinyinHelper;
import net.sourceforge.pinyin4j.format.*;
import net.sourceforge.pinyin4j.format.exception.BadHanyuPinyinOutputFormatCombination;

/**
 * ˵��:
 * 
 * @author fc6029585@163.com
 * @version 2008-8-22
 */

public class PinYinUtils {
	/* static fields */
	private static HanyuPinyinOutputFormat format;

	/* static methods */
	/** ��������ĺ��ֻ�ȡ��Ӧ��ƴ��,Ĭ�Ϲ���-Сд�������� */
	public static String getFullPinYinByString(String hanZi) {
		StringBuffer sb = new StringBuffer("");
		for (int i = 0; i < hanZi.length(); i++) {
			char oneChinese = hanZi.charAt(i);
			// ����Ǻ���
			if (java.lang.Character.toString(oneChinese).matches(
					("[\\u4E00-\\u9FA5]+"))) {
				sb = sb.append(PinYinUtils.getFullPinyinByChar(oneChinese));
				// String oneStr=PinYinUtils.getFullPinyinByChar(oneChinese);
			}
		}
		return sb.toString();
	}

	/** ��ƴ�����ַ���������������γ�������ƴ��,��Ϊ�ж����֣��� ',' �����ַ����ֿ��� */
	private static String concatPinyinStringArray(String[] pinyinArray) {
		StringBuffer pinyinStrBuf = new StringBuffer();
		if ((null != pinyinArray) && (pinyinArray.length > 0)) {
			for (int i = 0; i < pinyinArray.length; i++) {
				pinyinStrBuf.append(pinyinArray[i]);
				if (pinyinArray.length > 1 && i < pinyinArray.length - 1) {
					pinyinStrBuf.append(",");
				}
				// pinyinStrBuf.append(System.getProperty("line.separator"));
			}
		}
		pinyinStrBuf.append(" ");
		String result = pinyinStrBuf.toString();
		return result;
	}

	/** ��ȡchar��Ӧ�����ĵ�ƴ��,Ĭ�Ϲ���Сд�������ֱ�ʾ���� ,��Ϊ�ж����֣����Է���List */
	private static String getFullPinyinByChar(char chinesechar) {
		String result;
		if (chinesechar == ' ') {
			NullPointerException ex = new NullPointerException();
			System.out
					.println("**** cn.com.siwi.j2se.fc.utils.PinYinUtils.getFullPinyinByChar() ƴ��ת���쳣1111********");
			result = null;
			throw ex;
		} else {
			try {
				format = new HanyuPinyinOutputFormat();
				format.setCaseType(HanyuPinyinCaseType.LOWERCASE);
				format.setToneType(HanyuPinyinToneType.WITHOUT_TONE);
				String[] headArray = PinyinHelper.toHanyuPinyinStringArray(
						chinesechar, format);
				result = PinYinUtils.concatPinyinStringArray(headArray);
			} catch (Exception e) {
				e.printStackTrace();
				System.out
						.println("**** cn.com.siwi.j2se.fc.utils.PinYinUtils.getFullPinyinByChar() ƴ��ת���쳣2222********");
				result = null;
			}
		}
		return result;
	}

	/** ��ȡ�������Ķ�Ӧ�Ķ�Ӧ ����ĸ��ƴ���������֣�������һ�����飩 */
	// ��������������
	public static String getHeadCharByString(String chineses) {
		if (chineses == null) {
			System.out.println(" \n ��ȡ��������ĸ�쳣 \n");
			return null;
		}
		// ���
		StringBuffer headsb = new StringBuffer();
		String input = PinYinUtils.getFullPinYinByString(chineses);
		// Ȼ������Ƿָ�
		String[] hanZi = input.split("\\s");
		for (int i = 0; i < hanZi.length; i++) {
			String[] duoYinJie = hanZi[i].split(",");
			if (duoYinJie.length == 1) {// ��ǰֻ��length==1
				try {
					headsb = headsb.append(duoYinJie[0].charAt(0) + "-");
				} catch (Exception e) {
					System.out
							.println("PinYinUtils.java line of 96 here was wrong!"
									+ " chinese is :" + chineses);
					headsb = headsb.append("-");
				}
			} else if (duoYinJie.length > 1) {
				for (int j = 0; j < duoYinJie.length; j++) {
					// System.out.println(duoYinJie[j]+" ============");
					// String[] haveDuoYinJie = duoYinJie[i].split(",");
					// for (int a = 0; a < haveDuoYinJie.length; a++)
					// {
					// System.out.println(haveDuoYinJie[a]+" ----------");
					// headsb = headsb.append(haveDuoYinJie[a].charAt(0));
					// }
					headsb = headsb.append(duoYinJie[j].charAt(0));
				}
				headsb = headsb.append("-");
			}
		}
		return headsb.toString();
	}

	public static void main(String[] args)
			throws BadHanyuPinyinOutputFormatCombination {
		
        Hanyu hanyu = new Hanyu();

        // ��Ӣ�Ļ�ϵ�һ������

        String str = "��Ϫ��ʯ�����캮��Ҷϡ�� ɽ·Ԫ���꣬ �մ�ʪ���¡�";

        String strPinyin = hanyu.getStringPinYin(str);

        System.out.println(strPinyin);

	}

	/* constructors */

	/* fields */

	/* methods */

	/* extends methods */

	/* implements methods */

	/* properties */
}
class Hanyu
{
         private HanyuPinyinOutputFormat format = null;
         private String[] pinyin;
        
         public Hanyu()
         {
                   format = new HanyuPinyinOutputFormat();
                   //format.setToneType(HanyuPinyinToneType.WITHOUT_TONE);
                   pinyin = null;
         }

       
         //ת�������ַ�
         public String getCharacterPinYin(char c)
         {
               try
               {
            	   pinyin = PinyinHelper.toHanyuPinyinStringArray(c, format);
               }
               catch(BadHanyuPinyinOutputFormatCombination e)
               {
                   e.printStackTrace();
               }
              
               // ���c���Ǻ��֣�toHanyuPinyinStringArray�᷵��null
               if(pinyin == null) return null;
              
               // ֻȡһ������������Ƕ����֣���ȡ��һ������
               return pinyin[0];   
         }
        
         //ת��һ���ַ���
         public String getStringPinYin(String str)
         {
               StringBuilder sb = new StringBuilder();
               String tempPinyin = null;
               for(int i = 0; i < str.length(); ++i)
               {
                    tempPinyin =getCharacterPinYin(str.charAt(i));
                    if(tempPinyin == null)
                    {
                             // ���str.charAt(i)�Ǻ��֣��򱣳�ԭ��
                            sb.append(str.charAt(i));
                    }
                    else
                    {
                            sb.append(tempPinyin);
                    }
               }
               return sb.toString();
         }
}