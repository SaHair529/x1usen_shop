Интеграция с ABCP:
1) Отправка запроса на доставку в ТК при оформлении заказа


--------------------------------------------------------------------------------------------------------------------------------------
[comment]: <> (1&#41;Видимая граница шапки - 0)

[comment]: <> (2&#41;Не удалось провести расчёты, вы можете провести расчёты вручную - 0)

[comment]: <> (3&#41;Изменить форму текста информации &#40;на почте есть пример&#41; - 0)

[comment]: <> (4&#41;Подтверждение оформления заказа - 0)
-- Добавить в текст подтверждения оформления формы заказа сумму заказа

[comment]: <> (5&#41;Убираем регистрацию юр. лиц - 1)

[comment]: <> (6&#41;При нажатии на минус товара, если в корзине остался один товар предупреждать о том, что товар уйдет из корзины - 1)

[comment]: <> (7&#41;Окошко с данными о товаре в корзине - 2)
8)Расчёт до терминала dellin - 2
9)Вопросительный знак рядом со статусом заказа - 2

[comment]: <> (10&#41;Количество товаров на значке корзины - 3)
!!Мало места для отображения количества товаров

[comment]: <> (11&#41;Добавить статус оплаты в заказы - 3)
12)Дописать UpdateDellinTerminalsCommand, чтобы он делал запрос в АПИ деловых линий для получения инфы о терминалах - 4

[comment]: <> (13&#41;Отдельный калькулятор деловых линий - 5)
-- Добавить обработчик статуса 400 (Можно найти в TODO Phpstorm)

14)Поисковик в дереве - 7
15)Контроль клиента и его заказов через админку - 10
16)Разобраться, почему в vps в Gmail AccessToken нет refresh token