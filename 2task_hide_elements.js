/**
 * hide_elements.js
 *
 * Алгоритм:
 * 1. На DOMContentLoaded ищем select[name="type_val"].
 * 2. Скрываем все input[type="text"] (и их ближайшие <p>).
 * 3. Показываем только те input[type="text"], у которых name содержит выбранное значение.
 * 4. Подписываемся на change у select и повторяем логику.
 * Это очень простой алгоритм, поэтому для простой задачи он лучше всего и подходит.
 *
 *
 * Другие возможные алгоритмы:
 * За один цикл по всем полям проверяем, содержит ли name выбранное значение.
 * Для каждого поля сразу устанавливается display: '' (показать) или display: 'none' (скрыть).
 *
 * Если каждое поле находится внутри отдельного контейнера (например, <p>),
 * то скрывается/показывается целиком контейнер, а не само поле.
 * Для каждого контейнера проверяется, содержит ли вложенное поле подходящий name.
 *
 * При первом выполнении запоминается, какие поля были показаны.
 * При следующем изменении скрываются только поля из предыдущей показанной группы,
 * а показываются — только поля из новой группы.
 *
 */
(function() {
    function updateFieldsVisibility() {
        const typeSelect = document.querySelector('select[name="type_val"]');
        if (!typeSelect) return;
        const selectedValue = typeSelect.value;

        const allTextFields = document.querySelectorAll('input[type="text"]');
        allTextFields.forEach(field => {
            const parentP = field.closest('p');
            if (parentP) parentP.style.display = 'none';
            field.style.display = 'none';
        });

        const matchingFields = document.querySelectorAll(`input[type="text"][name="input_${selectedValue}"]`);
        matchingFields.forEach(field => {
            const parentP = field.closest('p');
            if (parentP) parentP.style.display = '';
            field.style.display = '';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.querySelector('select[name="type_val"]');
        if (typeSelect) {
            updateFieldsVisibility();
            typeSelect.addEventListener('change', updateFieldsVisibility);
        }
    });
})();
