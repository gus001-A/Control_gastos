import message from 'ant-design-vue/es/message';
import Modal from 'ant-design-vue/es/modal';

message.config({ top: '16px', duration: 3, maxCount: 3 });

export function exito(texto) {
    message.success(texto);
}

export function error(texto) {
    message.error(texto);
}

export function advertencia(texto) {
    message.warning(texto);
}

/**
 * Reemplaza al viejo <ConfirmDialog>: confirmar({ title, content, danger, onOk }).
 */
export function confirmar({ title = '¿Estás seguro?', content = '', danger = true, okText = 'Eliminar', onOk }) {
    Modal.confirm({
        title,
        content,
        okText,
        cancelText: 'Cancelar',
        okType: danger ? 'danger' : 'primary',
        centered: true,
        maskClosable: true,
        onOk,
    });
}
