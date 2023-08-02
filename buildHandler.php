<?php

function buildHandler($h_name,$h_code,$f_url){
    $pay = array
	(
        'NAME' => $h_name, 							                        // Название обработчика
        'SORT' => 100, 											            // Сортировка
        'CODE' => $h_code, 							                        // Уникальный код обработчика в системе
        'SETTINGS' => array
        ( 											                        // Настройки обработчика
            'CURRENCY' =>  array
            (
                'RUB'
            ), 																// Список валют, которые поддерживает обработчик
            'FORM_DATA'  =>  array
            ( 																// Настройки формы
                'ACTION_URI' => $f_url,                                   // URL, на который будет отправляться форма
                'METHOD' => 'POST', 										// Метод отправки формы
                'FIELDS' => array  
                (                                                           // Карта соответствия полей между полями формы и параметрами обработчика: массив вида array(код_поля => код_параметра_обработчика)
                    'orderId' => array 
                    (
                        'CODE' => 'ORDER_ID',
                        'VISIBLE' => 'N'
                    ),
                    'paymentId'	=> array
                    (
                        'CODE' => 'PAYMENT_ID', 
                        'VISIBLE' => 'N'
                    ),
                    'vatProduct' => array
                    (
                        'CODE' => 'VAT_PRODUCT',
                        'VISIBLE' => 'N'
                    ),
                    'vatDelivery' => array
                    (
                            'CODE' => 'VAT_DELIVERY',
                            'VISIBLE' => 'N'
                    ),
                    'sum'	=> array
                    (
                        'CODE' => 'SUM', 
                        'VISIBLE' => 'N'
                    ),
                    'BX_SYSTEM_ID'	=> array
                    (
                        'CODE' => 'BX_SYSTEM_ID', 
                        'VISIBLE' => 'N'
                    )
                ),
            ),
            'CODES' => array
            (											                    // Список параметров обработчика
                "ORDER_ID" => array  
                (
                    "NAME" => 'Код закзаза',
                    "DESCRIPTION" => 'Код закзаза',
                    'SORT' => 100,
                    'GROUP'  => 'ORDER',
                    'DEFAULT'  => array
                    (
                        'PROVIDER_KEY' => 'ORDER',
                        'PROVIDER_VALUE' => 'ID'
                    )
                ),
                "PAYMENT_ID" => array  
                (
                    "NAME" => 'Код платежа',
                    "DESCRIPTION" => 'Код платежа',
                    'SORT' => 100,
                    'GROUP'  => 'PAYMENT',
                    'DEFAULT'  => array
                    (
                        'PROVIDER_KEY' => 'PAYMENT',
                        'PROVIDER_VALUE' => 'ID'
                    )
                ),
                "VAT_PRODUCT" => array
                (
                    "NAME" => 'НДС для всех товаров',
                    'SORT' => 400,
                    "DESCRIPTION" => 'НДС для всех товаров приминимое при оплате'

                ),
                "VAT_DELIVERY" => array
                (
                    "NAME" => 'НДС для доставки',
                    'SORT' => 400,
                    "DESCRIPTION" => 'НДС для доставки приминимое при оплате'

                ),
                "SUM" => array  
                (
                    "NAME" => 'Сумма оплаты',
                    'SORT' => 400,
                    'GROUP' => 'PAYMENT',
                    'DEFAULT' => array  
                    (
                        'PROVIDER_KEY' => 'PAYMENT',
                        'PROVIDER_VALUE' => 'SUM'
                    ),
                ),
                "BX_SYSTEM_ID" => array  
                (
                    "NAME" => 'Код платежной системы',
                    'SORT' => 400,
                    'GROUP' => 'ORDER',
                    'DEFAULT' => array  
                    (
                        'PROVIDER_KEY' => 'ORDER',
                        'PROVIDER_VALUE' => 'PAY_SYSTEM_ID'
                    ),
                ),

            ),
        ),
	);
    return $pay;
}
?>