<?php
/**
 * @copyright rareMVC 
 * @author duwei
 *验证类型
 */
class rValidate_type {
    /**
     * 必填
     */
    const Required='required';

    /**
     * 最小长度
     */
    const MinLength='minlength';

    /**
     * 最大长度
     */
    const MaxLength='maxlength';

    /**
     * 与指定值相等
     */
    const EqualTo='equalTo';

    /**
     * email地址
     */
    const Email='email';

    /**
     * url 如http://rare.hongtao3.com
     */
    const Url='url';


    /**
     * 日期  eg 2012-12-12
     */
    const DateISO='dateISO';

    /**
     * 数字
     */
    const Number='number';

    /**
     * 最小值
     */
    const Min='min';

    /**
     * 最大值
     */
    const Max='max';


    /**
     * 枚举
     */
    const Enum='enum';
    
    /**
     * 长度之间
     */
    const Rangelength="rangelength";
    /**
     * 数值之间
     */
    const Range="range";
    
    /**
     * 指定长度
     */
    const Length="length";
    
    /**
     * 电话
     */
    const Phone="phone";
    
    /**
     * 整形
     */
    const Intger="int";
    
    /**
     * 邮政编码
     */
    const PostCode="postCode";
    
    /**
     * 最多允许的单纯数目
     */
    const MaxWords="maxWords";
    
    /**
     * 域名，如：rare.hongtao3.com
     */
    const Domain='domain';
    
    /**
     * 时间
     */
    const Time="time";
    
    /**
     *  比指定值小
     */
    const  SmallThan='smallThan';
    
    /**
     * 值不能等于
     */
    const NotIn='notIn';

    /**
     * 正则表达式
     */
    const Regex='regex';
    
}
?>
