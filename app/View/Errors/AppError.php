<?php

class AppError extends ErrorHandler {

/**
 * Security Error
 *
 * @return void
 */
    public function securityError() {
        $this->controller->set(array(
            'referer' => $this->controller->referer(),
        ));
        $this->_outputMessage('security');
    }
}
?>