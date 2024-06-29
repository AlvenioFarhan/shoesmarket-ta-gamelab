{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' %} {% endblock %}

<!-- tambah data barang -->
{% block edit_barang %} {% include '/admin/edit-barang.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
