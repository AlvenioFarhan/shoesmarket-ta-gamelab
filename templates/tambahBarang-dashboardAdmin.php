{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' %} {% endblock %}

<!-- tambah data barang -->
{% block tambah_barang %} {% include '/admin/tambah-barang.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
