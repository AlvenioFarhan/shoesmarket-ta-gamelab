{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' %} {% endblock %}

<!-- edit data user -->
{% block edit_user %} {% include '/admin/edit-user.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
