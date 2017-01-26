package org.intel.misc;

import net.sourceforge.pinyin4j.PinyinHelper;
import net.sourceforge.pinyin4j.format.*;
import net.sourceforge.pinyin4j.format.exception.BadHanyuPinyinOutputFormatCombination;

/**
 * 说明:
 * 
 * @author fc6029585@163.com
 * @version 2008-8-22
 */

public class PinYinUtils {
	/* static fields */
	private static HanyuPinyinOutputFormat format;

	/* static methods */
	/** 根据输入的汉字获取对应的拼音,默认规则-小写，无数字 */
	public static String getFullPinYinByString(String hanZi) {
		StringBuffer sb = new StringBuffer("");
		for (int i = 0; i < hanZi.length(); i++) {
			char oneChinese = hanZi.charAt(i);
			// 如果是汉字
			if (java.lang.Character.toString(oneChinese).matches(
					("[\\u4E00-\\u9FA5]+"))) {
				sb = sb.append(PinYinUtils.getFullPinyinByChar(oneChinese));
				// String oneStr=PinYinUtils.getFullPinyinByChar(oneChinese);
			}
		}
		return sb.toString();
	}

	/** 将拼音的字符数组组合起来，形成完整的拼音,因为有多音字（用 ',' 将各种发音分开） */
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

	/** 获取char对应的中文的拼音,默认规则，小写，无数字表示声调 ,因为有多音字，所以返回List */
	private static String getFullPinyinByChar(char chinesechar) {
		String result;
		if (chinesechar == ' ') {
			NullPointerException ex = new NullPointerException();
			System.out
					.println("**** cn.com.siwi.j2se.fc.utils.PinYinUtils.getFullPinyinByChar() 拼音转换异常1111********");
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
						.println("**** cn.com.siwi.j2se.fc.utils.PinYinUtils.getFullPinyinByChar() 拼音转换异常2222********");
				result = null;
			}
		}
		return result;
	}

	/** 获取输入中文对应的对应 首字母的拼音，多音字（所以是一个数组） */
	// 变量命名不好命
	public static String getHeadCharByString(String chineses) {
		if (chineses == null) {
			System.out.println(" \n 获取汉字首字母异常 \n");
			return null;
		}
		// 结果
		StringBuffer headsb = new StringBuffer();
		String input = PinYinUtils.getFullPinYinByString(chineses);
		// 然后把他们分割
		String[] hanZi = input.split("\\s");
		for (int i = 0; i < hanZi.length; i++) {
			String[] duoYinJie = hanZi[i].split(",");
			if (duoYinJie.length == 1) {// 以前只有length==1
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

        // 中英文混合的一段文字

        String str = "荆溪白石出，天寒红叶稀。 山路元无雨， 空翠湿人衣。";

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

       
         //转换单个字符
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
              
               // 如果c不是汉字，toHanyuPinyinStringArray会返回null
               if(pinyin == null) return null;
              
               // 只取一个发音，如果是多音字，仅取第一个发音
               return pinyin[0];   
         }
        
         //转换一个字符串
         public String getStringPinYin(String str)
         {
               StringBuilder sb = new StringBuilder();
               String tempPinyin = null;
               for(int i = 0; i < str.length(); ++i)
               {
                    tempPinyin =getCharacterPinYin(str.charAt(i));
                    if(tempPinyin == null)
                    {
                             // 如果str.charAt(i)非汉字，则保持原样
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